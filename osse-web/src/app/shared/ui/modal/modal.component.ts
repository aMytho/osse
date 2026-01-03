import { AfterViewInit, ChangeDetectionStrategy, ChangeDetectorRef, Component, ComponentRef, ElementRef, ViewChild, ViewContainerRef, inject } from '@angular/core';
import { ModalService } from './modal.service';

@Component({
  selector: 'app-modal',
  imports: [],
  templateUrl: './modal.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
  styleUrl: './modal.styles.css'
})
export class ModalComponent implements AfterViewInit {
  // Parent Modal
  @ViewChild('modal') modal!: ElementRef<HTMLDialogElement>;
  // VCR (dynamic content)
  @ViewChild('vcr', { static: true, read: ViewContainerRef }) vcr!: ViewContainerRef;
  title: string = '';
  // Active component
  component!: ComponentRef<any>;
  cdr = inject(ChangeDetectorRef);

  constructor(private modalService: ModalService) { }

  public open() {
    this.modal.nativeElement.showModal();
  }

  public close() {
    this.modal.nativeElement.close();
    this.component.destroy();
    this.title = '';
  }

  ngAfterViewInit(): void {
    this.modalService.onLoadComponent.subscribe((v) => {
      // Clear the old component and save the new one
      this.vcr.clear();
      // Load the component
      this.component = this.vcr.createComponent(v[0] as any);
      this.title = v[1];

      // Set any input props
      (v[2] ?? []).forEach((input: { name: string, val: any }) => {
        this.component.instance[`${input.name}`] = input.val;
      });

      // Show changes
      this.cdr.detectChanges();

      // Listen for close events
      this.component.instance['onClose'].subscribe((_: any) => this.close());
    });

    this.modalService.onShow.subscribe(() => this.open());
    this.modalService.onClose.subscribe(() => this.close());
  }
}

